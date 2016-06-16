select batchproject_id,projectname,projectstatus,credittype,credit,convert(varchar(10),certficatetime,20) as certficatetime
from Creditbatchproject where batchproject_id=:id