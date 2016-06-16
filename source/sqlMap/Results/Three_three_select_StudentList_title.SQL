SELECT distinct scores.COURSENO+scores.[GROUP] AS kh,
COURSES.COURSENAME as km,COURSES.SCHOOL,scores.YEAR as year,scores.TERM as term,scores.YEAR+1 as year2,
SCHOOLS.NAME AS SCHOOLNAME,isnull(scheduleplan.ps,0) as ps
FROM scores INNER JOIN COURSES ON scores.COURSENO=COURSES.COURSENO
INNER JOIN SCHOOLS ON COURSES.SCHOOL=SCHOOLS.SCHOOL
left outer join  scheduleplan on scheduleplan.courseno+scheduleplan.[group]=scores.courseno+scores.[group]
and scheduleplan.year=scores.year and scheduleplan.term=scores.term
WHERE scores.YEAR=:YEAR
AND scores.TERM=:TERM
AND scores.COURSENO+scores.[GROUP]=:COURSENO
GROUP BY scores.COURSENO + scores.[GROUP],
      COURSES.COURSENAME, COURSES.SCHOOL,scores.YEAR, scores.TERM,
      scores.YEAR + 1, SCHOOLS.NAME,scheduleplan.ps