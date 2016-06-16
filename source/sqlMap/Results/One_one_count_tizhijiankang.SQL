select count(*) as ROWS from(SELECT row_number() over(order by 学年) as row,学年 as xn,成绩 as cj,分数 as xf,测试日期 as csrq
from 体质健康测试成绩
where 学号=:STUDENTNO
) as b