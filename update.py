#!/usr/bin/python3
import os
import sys

baseDir = "/home/xc_vm/"
PHPDir = baseDir + "bin/php/bin/php"
archive_file = baseDir + "tmp/update.tar.gz"

if __name__ == "__main__":
    # stop xc_vm
    os.system("sudo systemctl stop xc_vm")
    # extract archive
    os.system('sudo tar -zxvf "%s" -C "%s"' % (archive_file, baseDir))
    # add permissions
    os.system('sudo chown -R xc_vm:xc_vm "%s"' % baseDir)
    # os.system(f"sudo sh {baseDir}permissions.sh")
    # Transferring control further
    os.system("sudo %s %sincludes/cli_tool/update.php post-update" % (PHPDir, baseDir))
    # start xc_vm
    os.system("sudo systemctl start xc_vm")
    # remove archive
    os.remove(archive_file)
    sys.exit(1)
