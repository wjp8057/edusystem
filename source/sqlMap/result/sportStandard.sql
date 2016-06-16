SELECT 学年 AS YEAR,成绩 AS RESULT,分数 AS CREDITS,CONVERT(varchar(10) , 测试日期, 120 ) AS SDATE
from 体质健康测试成绩
where 学号=:STUDENTNO