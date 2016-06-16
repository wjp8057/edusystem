select count(*) as ROWS from(select row_number() over(order by t.teacherno) as row,t.courseno+t.[group] as courseno,t.teacherno,
t.teachername,t.classno,S.NAME,t.credit,ca.[VALUE] as xklx,ct.[VALUE] as kclx,mzks,
t.jiaozhengxishu,w.work,w.work*w.CFXS as cfgzl,w.CFXS
from totalwork t
inner join work w on t.Totalwork_id=w.Totalwork_id
inner join COURSETYPEOPTIONS ct on ct.NAME=t.kclx
inner join COURSEAPPROACHES ca on ca.NAME=t.xklx
inner join SCHOOLS S ON S.SCHOOL=t.SCHOOL
where t.classno like :CLASSNO and RTRIM(t.courseno)+t.[group] like :COURSENO
and t.teacherno like :TEACHERNO and t.code
like :CODE  and t.SCHOOL
like :SCHOOL
) as b







