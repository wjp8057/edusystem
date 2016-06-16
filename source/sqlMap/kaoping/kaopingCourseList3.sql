select count(*) as ROWS
from 教学质量评估综合 inner join teachers on 教学质量评估综合.teacherno=teachers.teacherno
inner join courses on courses.courseno=substring(教学质量评估综合.courseno,1,7)
inner join 教学质量评估详细 on 教学质量评估综合.recno=教学质量评估详细.map
where studentno=:STUDENTNO AND 教学质量评估综合.ENABLED='1'
and year=:year and term=:term
and ISNULL(rank,0)=0
