import time
a, b = [int(_) for _ in input().split()]
# AC
if a == 3:
    print(7)
# WA
elif a == 1:
    print(5)
# MLE
elif a == 19:
    some_str = ' ' * 512000000
# TLE (cpu/sys)
elif a == 6:
    for i in range(3000000000):
        n = 3-1
# TLE (real)
elif a == 100:
    time.sleep(300)
