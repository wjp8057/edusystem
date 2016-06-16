select count(*) as ROWS from(select row_number() over(order by b.projectname) as row,b.projectname,b.credit,b.zrs,c.weishenhe from(
select
xm.batchproject_id,xm.projectname,xm.credit,xm.credittype,count(gr.project_id) as zrs
from Creditbatchproject xm inner join Creditbatchfirm as gr on xm.batchproject_id=gr.project_id where
xm.projectstatus=1 AND xm.credittype=:ctype and xm.projectname
like :pname  and xm.p_status=1 and xm.zhongshen=:zhongshen and gr.year=:yone and gr.term=:tone
group by xm.batchproject_id,xm.projectname,xm.credit,xm.credittype
) as b left join (
select project_id,count(project_id) as weishenhe from Creditbatchfirm where Final_status=3 and year=:ytwo and term=:ttwo
group by project_id
) as c on b.batchproject_id=c.project_id
) as bb