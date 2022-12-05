# def time_delta(t1, t2):
#     t1 = t1.split()
#     t2 = t2.split()
#     t1_ora = t1[4].split(':')
#     t2_ora = t2[4].split(':')
#     time = ((int(t1[3]) - int(t2[3])) * 365 + (int(t1[1]) - int(t2[1]))) * 24 * 3600 + (int(t1_ora[0]) - int(t2_ora[0])) * 3600 + (int(t1_ora[1]) - int(t2_ora[1])) * 60 + (int(t1_ora[2]) -  int(t2_ora[2]))
#     if t1[5][0] == "+":
#         time -= int(t1[5][1]) * 10 * 3600 + int(t1[5][2]) * 3600 + int(t1[5][3]) * 10 * 60 + int(t1[5][4]) * 60
#     else:
#         time += int(t1[5][1]) * 10 * 3600 + int(t1[5][2]) * 3600 + int(t1[5][3]) * 10 * 60 + int(t1[5][4]) * 60
#     if t2[5][0] == "+":
#         time -= int(t2[5][1]) * 10 * 3600 + int(t2[5][2]) * 3600 + int(t2[5][3]) * 10 * 60 + int(t2[5][4]) * 60
#     else:
#         time += int(t2[5][1]) * 10 * 3600 + int(t2[5][2]) * 3600 + int(t2[5][3]) * 10 * 60 + int(t2[5][4]) * 60
#     return abs(time)

# print(time_delta("Sun 10 May 2015 13:54:36 -0700","Sun 10 May 2015 13:54:36 -0000"))

# n = int(input())
# arr = map(int, input().split())
# arr = sorted(arr)
# max = arr[len(arr)-1]
# print(len(arr))
# for i in range(len(arr)-2,-1):
#     print(i)
#     if max != arr[i]:
#         print(arr[i])
#         break

# def swap_case(s):
#     for i in range(len(s)):
#         if s[i].isupper():
#             s[i] += 32
#     print(s)

# swap_case("pOOOp")

# print(list(set([1,2,3,4,5])))

# foo = {"a":"b","c":"d"}
# bar = {"c":"d","e":"f","g":"h"}
# # print(foo.append([{k:v} for k,v in bar.items()]))
# print(foo["d"])

def foobarbaz(foo: int, *, bar: int, baz:int) -> int:
    return foo + bar + baz
print(foobarbaz(foo=3,bar=4,baz=5))