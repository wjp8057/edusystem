select * from(select row_number() over(order by createdate)as row,
p_status,batchproject_id,projectname,credit,credittype,
convert(varchar(10),certficatetime,20)as certficatetime
from Creditbatchproject where projectstatus=1
AND (credittype=:cone or credittype=:ctwo)  and
 projectname like :projectname and (p_status=:pone or p_status=:ptwo) and
zhongshen=:zhongshen)as b where b.row between :start and :end