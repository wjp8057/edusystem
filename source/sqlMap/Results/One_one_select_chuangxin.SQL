select * from(select row_number() over(order by [date]) as row,reason as hdyy,credit xf,convert(varchar(10),[date],20) rdsj,year sqxn
from addcredit
where studentno=:STUDENTNO
) as b where b.row between :start and :end