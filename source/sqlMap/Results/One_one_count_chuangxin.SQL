select count(*) as ROWS from(select row_number() over(order by [date]) as row,reason as hdyy,credit xf,[date] rdsj,year sqxn
from addcredit
where studentno=:STUDENTNO
) as b