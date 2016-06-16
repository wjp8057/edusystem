select * from (select row_number() over(order by 教学质量评估详细.rank) as row,teachers.name as TEACHERNAME,
教学质量评估详细.MAP,
教学质量评估详细.compelete,
教学质量评估综合.courseno as COURSENO,courses.coursename as COURSENAME,
CASE [task]
      WHEN 'B' THEN '毕业实践'
      WHEN 'K' THEN '理论课'
      WHEN 'C' THEN '理实一体'
      WHEN 'S' THEN '实践课'
      END AS TYPE,教学质量评估综合.recno as RECNO,
      教学质量评估详细.recno as RECNO2,
      RTRIM(rank) as rank,
case [compelete]
      WHEN 1 THEN RTRIM(教学质量评估详细.TOTAL)+'分'
      ELSE '未评'
      END AS FRACTION,教学质量评估综合.YEAR  YEAR,教学质量评估综合.TERM AS TERM
from 教学质量评估综合 inner join teachers on 教学质量评估综合.teacherno=teachers.teacherno
inner join courses on courses.courseno=substring(教学质量评估综合.courseno,1,7)
inner join 教学质量评估详细 on 教学质量评估综合.recno=教学质量评估详细.map
where studentno=:STUDENTNO AND 教学质量评估综合.ENABLED='1'
and year=:year and term=:term
) as b where b.row=:rank
