select count(*) as ROWS from(select row_number() over(order by t.Courseno) as row,t.Courseno,t.Coursename,t.credit,t.Estimate,t.Attendent,t.W_number,
ca.[VALUE] as xklx,cp.[VALUE] as kclx,t.mzks,t.Standard,t.code,w.work,t.classname,t.teachername
from totalwork t inner join work w on t.Totalwork_id=w.Totalwork_id
inner join COURSETYPEOPTIONS cp ON t.kclx=cp.NAME
inner join COURSEAPPROACHES ca ON t.xklx=ca.NAME
where t.Courseno like :COURSENO AND t.classno like :CLASSNO and t.teacherno like :TEACHERNO and t.code like :CODE and t.school like :SCHOOL

) as b