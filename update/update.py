#!/usr/bin/python3
import os
import sys

baseDir = "/home/xtreamcodes/"
PHPDir = baseDir + "bin/php/bin/php"

if __name__ == "__main__":
    os.system(f"sudo sh {baseDir}service.sh stop")
    os.system(f"rsync -a {baseDir}updates/update_tmp/ {baseDir}")
    os.system('sudo chown -R xtreamcodes:xtreamcodes "%s"' % baseDir)
    os.system(f"sudo sh {baseDir}permissions.sh")
    os.system("sudo %s %supdates/update.php" % (PHPDir, baseDir))
    os.system(f"sudo sh {baseDir}service.sh start")
    sys.exit(1)
