ulimit -v 
timeout  ./a.out < .in 2>&1
echo $?