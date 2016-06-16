select  * from (select row_number() over(order by Courseno) as row,t.Totalwork_id,t.Courseno,t.Coursename,t.Standard,t.code,ct.[VALUE] kclx,t.credit,t.school,t.mzks,ca.[VALUE] xklx,t.[group]
from totalwork  as t inner join COURSETYPEOPTIONS as ct on t.kclx=ct.NAME
inner join COURSEAPPROACHES as ca on t.xklx=ca.NAME
where Courseno like :courseno and Coursename like :coursename and kclx like :kclx and xklx like :xklx
and school like :school and (code=='M1'or code='M8') and status=0) as b where b.row between :start and :end