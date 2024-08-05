import sys
import json
import os
from itertools import cycle, zip

rConfigPath = "/home/xtreamcodes/config"


def doDecrypt():
    rDecrypt = decrypt()
    if rDecrypt:
        print("Server ID: %s%d" % (" " * 10, int(rDecrypt["server_id"])))
        print("Host: %s%s" % (" " * 15, rDecrypt["host"]))
        print("Port: %s%d" % (" " * 15, int(rDecrypt["db_port"])))
        print("Username: %s%s" % (" " * 11, rDecrypt["db_user"]))
        print("Password: %s%s" % (" " * 11, rDecrypt["db_pass"]))
        print("Database: %s%s" % (" " * 11, rDecrypt["db_name"]))
    else:
        print("Config file could not be read!")


def decrypt():
    try:
        return json.loads(
            "".join(
                chr(ord(c) ^ ord(k))
                for c, k in zip(
                    open(rConfigPath, "rb").read().decode("base64"),
                    cycle("5709650b0d7806074842c6de575025b1"),
                )
            )
        )
    except Exception:
        return None


def encrypt(rInfo):
    try:
        os.remove(rConfigPath)
    except Exception:
        pass
    rf = open(rConfigPath, "wb")
    rf.write(
        "".join(
            chr(ord(c) ^ ord(k))
            for c, k in zip(
                '{"host":"%s","db_user":"%s","db_pass":"%s","db_name":"%s","server_id":"%d", "db_port":"%d"}'
                % (
                    rInfo["host"],
                    rInfo["db_user"],
                    rInfo["db_pass"],
                    rInfo["db_name"],
                    int(rInfo["server_id"]),
                    int(rInfo["db_port"]),
                ),
                cycle("5709650b0d7806074842c6de575025b1"),
            )
        )
        .encode("base64")
        .replace("\n", "")
    )
    rf.close()


if __name__ == "__main__":
    try:
        rCommand = sys.argv[1]
    except Exception:
        rCommand = None
    if rCommand and rCommand.lower() == "decrypt":
        doDecrypt()
    elif rCommand and rCommand.lower() == "encrypt":
        print("Current configuration")
        print(" ")
        doDecrypt()
        print(" ")
        rEnc = {"pconnect": 0}
        try:
            rEnc["server_id"] = int(input("Server ID: %s" % (" " * 10)))
            rEnc["host"] = input("Host: %s" % (" " * 15))
            rEnc["db_port"] = input("Port: %s" % (" " * 15))
            rEnc["db_user"] = input("Username: %s" % (" " * 11))
            rEnc["db_pass"] = input("Password: %s" % (" " * 11))
            rEnc["db_name"] = input("Database: %s" % (" " * 11))
            print(" ")
        except Exception:
            print("Invalid entries!")
            sys.exit(1)
        try:
            encrypt(rEnc)
            print("Written to config file!")
        except Exception:
            print("Couldn't write to file!")
    else:
        print("Usage: config.py [ENCRYPT | DECRYPT]")
