select * from(SELECT row_number() over(order by R33.PROGRAMNO) as row,R33.PROGRAMNO AS programno, PROGRAMS.PROGNAME AS progname,
      PROGRAMS.SCHOOL belongschool,R33.COURSENO AS courseno, COURSES1.COURSENAME coursename,
      COURSES1.CREDITS AS credits, COURSES1.SCHOOL AS createschool,
      R33.EQNO AS eqno, COURSES.COURSENAME AS eqname,
      COURSES.CREDITS AS eqcredits, COURSES.SCHOOL AS eqschool
FROM R33 INNER JOIN
      COURSES COURSES1 ON R33.COURSENO = COURSES1.COURSENO INNER JOIN
      PROGRAMS ON R33.PROGRAMNO = PROGRAMS.PROGRAMNO INNER JOIN
      COURSES ON R33.EQNO = COURSES.COURSENO
where R33.courseno like :COURSENO and R33.EQNO like :EQNO and R33.programno like :PROGRAMNO) as b where b.row between :strat and :end2
order by b.programno,b.courseno,b.eqno



