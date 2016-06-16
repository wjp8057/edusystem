select count(*) as ROWS from(select projectname,credittype,certficatetime,description,createdate,credit,schoolview,deanview,deansubtime
from creditsinglefirm where studentno=:studentno and credittype=:tp and year=:year and term=:term
) as b