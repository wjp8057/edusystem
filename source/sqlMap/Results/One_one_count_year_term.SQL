select count(*) as ROWS from(SELECT row_number() over(order by 学年,学期) as row,学年 as xn,
    case[学期]
    when '0' then '整学年'
    when '1' then '第一学期'
    when '2' then '第二学期+短学期'
    else '-'
    end as xq,学绩分 xjf
from 学绩分
where 学号=:STUDENTNO
) as b