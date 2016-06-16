select row_number() over(order by rank) as row,teachers.name as TEACHERNAME,
教学质量评估详细.MAP,
教学质量评估详细.compelete,
教学质量评估综合.courseno as COURSENO,courses.coursename as COURSENAME,
教学质量评估类型.NAME AS TYPE,教学质量评估综合.recno as RECNO,
      教学质量评估详细.recno as RECNO2,
      RTRIM(rank) as rank,
case [compelete]
      WHEN 1 THEN RTRIM(教学质量评估详细.TOTAL)+'分'
      ELSE '未评'
      END AS FRACTION,教学质量评估综合.YEAR  YEAR,教学质量评估综合.TERM AS TERM
from 教学质量评估综合 inner join teachers on 教学质量评估综合.teacherno=teachers.teacherno
inner join courses on courses.courseno=substring(教学质量评估综合.courseno,1,7)
inner join 教学质量评估详细 on 教学质量评估综合.recno=教学质量评估详细.map
inner join 教学质量评估类型 on 教学质量评估类型.TYPE=教学质量评估综合.[task]
where year=:year and term=:term AND 教学质量评估综合.ENABLED='1'and studentno=:STUDENTNO

