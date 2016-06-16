SELECT makeup.COURSENO AS kh,COURSES.COURSENAME kcmc,SCHOOLS.NAME AS kkxy,
      COUNT(*) AS xkrs,makeup.year as year,makeup.year+1 as year2,makeup.term as term
FROM makeup INNER JOIN
      COURSES ON makeup.COURSENO = COURSES.COURSENO INNER JOIN
      SCHOOLS ON SCHOOLS.SCHOOL=COURSES.SCHOOL
where makeup.courseno=:COURSENO and makeup.year=:YEAR and makeup.term=:TERM
GROUP BY MAKEUP.COURSENO,COURSES.COURSENAME,SCHOOLS.NAME,makeup.year,makeup.term
ORDER BY kh