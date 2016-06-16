select 学年 as xn,
学期 as xq,
课号 as kh,
课名 as km,
考试时间 as kssj,
人数 as rs,
      考场1 as js, 考场2, 考场3,考场1教师1 as tOne1, 考场1教师2 as tOne2,考场1教师3 as tOne3,
      考场2教师1 as tTwo1, 考场2教师2 as tTwo2, 考场2教师3 as tTwo3,
      考场3教师1 as tThree1, 考场3教师2 as tThree2, 考场3教师3 as tThree3,
      考场1人数 as renshu1,考场2人数 as renshu2,考场3人数 as renshu3,rem
from L_VIEW_ksap
where 课号=:courseno and 学年=:year and 学期=:term