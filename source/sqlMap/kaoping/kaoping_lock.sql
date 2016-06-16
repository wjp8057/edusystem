select count(*) as lock
from 教学质量评估详细 inner join 教学质量评估综合 on 教学质量评估综合.recno=教学质量评估详细.map 
where Compelete =0 and 教学质量评估详细.studentno=:studentno and 教学质量评估综合.enabled=1
group by 教学质量评估详细.studentno