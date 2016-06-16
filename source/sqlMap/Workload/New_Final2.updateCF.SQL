update work set CFXS=(
select case  when (isnull(bb.totalwork_id,'aa')<>'aa' or dd.code!='M1'  ) THEN 1 else 0.85 END as CFXS
from VIEW_CF dd
left join(
select dbo.getOne(b.renshu) as renshu,dbo.getOne(c.totalwork_id) as totalwork_id from
(select teacherno,courseno,max(Attendent) as renshu from totalwork where totalwork.status=4 group by courseno,teacherno) as b
left join totalwork c on (b.courseno=c.courseno and b.teacherno=c.teacherno and b.renshu=c.Attendent) where c.status=4
GROUP BY c.courseno,c.teacherno,b.renshu) as bb on dd.totalwork_id=bb.totalwork_id
where work.Totalwork_id=dd.Totalwork_id
 )
 where CF_status<>1