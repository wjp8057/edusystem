select 学年 as YEAR,学期 as TERM,课号 as COURSENO,课名 as COURSENAME,考试时间 as EXAMTIME,人数 as ATTENDENTS,
      考场1 as ROOMNO1, 考场2 as ROOMNO2, 考场3 as ROOMNO3,考场1教师1 as TEACHERS11, 考场1教师2 as TEACHERS12,考场1教师3 as TEACHERS13,
      考场2教师1 as TEACHERS21, 考场2教师2 as TEACHERS22, 考场2教师3 as TEACHERS23,考场3教师1 as TEACHERS31, 考场3教师2 as TEACHERS32, 考场3教师3 as TEACHERS33,
      考场1人数 as RS1,考场2人数 as RS2,考场3人数 as RS3,rem as REM
from L_VIEW_ksap
where 课号=:COURSENO and 学年=:YEAR and 学期=:TERM