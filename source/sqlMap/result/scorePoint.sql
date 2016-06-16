SELECT 学年 AS YEAR,
    case[学期]
    when '0' then '整学年'
    when '1' then '第一学期'
    when '2' then '第二学期+短学期'
    else '-'
    end as TERM,学绩分 as CREDITS
from 学绩分
where 学号=:STUDENTNO
order by YEAR,TERM