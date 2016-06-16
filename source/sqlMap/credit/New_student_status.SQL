select * from(select row_number() over(order by projectname) as row,applydate_id,projectname,credittype,convert(varchar(10),certficatetime,20)
as certficatetime,description,createdate,credit,schoolview,deanview,deansubtime
from creditsinglefirm where studentno=:studentno and credittype=:tp and year=:year and term=:term
) as b where b.row between :start and :end