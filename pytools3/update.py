#!/usr/bin/python3
import hashlib
import os
import sys

baseDir = os.path.dirname(os.path.realpath(__file__)) + "/"
PHPDir = baseDir + "php/bin/php"
toolsDir = baseDir + "tools/"


def md5(filename):
    MD5 = hashlib.md5()
    with open(filename, "rb") as file:
        for chunk in iter(lambda: file.read(4096), b""):
            MD5.update(chunk)
    return MD5.hexdigest()


def doUpdate(filename):
    os.system("kill $(ps aux | grep 'xtreamcodes' | grep -v grep | grep -v 'start_services.sh' | awk '{print $2}') 2>/dev/null")
    os.system('sudo tar -zxvf "%s" -C "%s"' % (filename, baseDir))
    os.system('sudo chown -R xtreamcodes:xtreamcodes "%s"' % baseDir)
    os.system(f"sudo {baseDir}permissions.sh")
    os.system('sudo %s %supdate.php "post-update"' % (PHPDir, toolsDir))
    os.system(f"sudo {baseDir}start_services.sh")
    os.remove(filename)
    return True


if __name__ == "__main__":
    try:
        filename = sys.argv[1]
        MD5 = sys.argv[2]
    except:
        print("Please run the update from the XtreamCodes Admin Interface.")
        sys.exit(1)
    if md5(filename) == MD5:
        doUpdate(filename)
    else:
        print("CRC ERROR")
    sys.exit(1)
