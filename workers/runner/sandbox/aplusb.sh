ulimit -v 64000
timeout 1 python3 ./sandbox/aplusb.py < ./sandbox/aplusb.in 2>&1
echo $?