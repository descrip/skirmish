ulimit -v 512000
timeout 3 python3 ./sandbox/prac.py < ./sandbox/prac.in 2>&1
echo $?