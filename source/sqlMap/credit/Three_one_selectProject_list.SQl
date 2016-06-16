select * from(select row_number() over(order by batchproject_id) as row,batchproject_id,projectname,projectstatus,credittype,credit,
convert(varchar(10),certficatetime,20) as certficatetime
from Creditbatchproject where (credittype=:cone or credittype=:ctwo) and projectname like :projectname
 AND zhongshen=:zhongshen
) as b where b.row between :start and :end