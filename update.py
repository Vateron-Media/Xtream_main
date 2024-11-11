#!/usr/bin/python3
import os
import sys

baseDir = "/home/xtreamcodes/"
PHPDir = baseDir + "bin/php/bin/php"
archive_file = baseDir + "tmp/update.tar.gz"

if __name__ == "__main__":
    # stop xtreamcodes
    os.system("sudo systemctl stop xtreamcodes")
    # extract archive
    os.system('sudo tar -zxvf "%s" -C "%s"' % (archive_file, baseDir))
    # add permissions
    os.system('sudo chown -R xtreamcodes:xtreamcodes "%s"' % baseDir)
    # os.system(f"sudo sh {baseDir}permissions.sh")
    # Transferring control further
    os.system("sudo %s %stools/update.php post-update" % (PHPDir, baseDir))
    # start xtreamcodes
    os.system("sudo systemctl start xtreamcodes")
    # remove update_tmp
    # os.system(f"rm -rf {baseDir}update/")
    sys.exit(1)
