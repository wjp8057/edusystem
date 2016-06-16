select reason,credit,CONVERT(varchar(10) , [date], 120 ) AS sdate,year
from addcredit
where studentno=:STUDENTNO
order by [date]