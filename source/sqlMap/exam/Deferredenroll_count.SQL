select count(*) as ROWS from(SELECT row_number() over(order by sxj.STUDENTNO,sxj.FLAG,TESTPLAN.COURSENO) as row,sxj.year xn, sxj.term xq, sxj.STUDENTNO xh, sxj.NAME xm, TESTPLAN.COURSeNO kh,
      COURSES.COURSENAME kcmc, sxj.FLAG cc, [L_考试时间表].name kssj,scores.recno,scores.examrem,sxj.school
FROM SCORES INNER JOIN
          (SELECT TESTPLAN.year, TESTPLAN.term, SCORES.STUDENTNO,
               TESTPLAN.FLAG, STUDENTS.NAME, students.school
         FROM TESTPLAN INNER JOIN
               SCORES ON TESTPLAN.year = SCORES.YEAR AND
               TESTPLAN.term = SCORES.TERM AND
               TESTPLAN.COURSeNO = SCORES.COURSENO + SCORES.[GROUP] AND
               TESTPLAN.year =:YONE AND TESTPLAN.term =:TONE INNER JOIN
               STUDENTS ON SCORES.STUDENTNO = STUDENTS.STUDENTNO
         GROUP BY TESTPLAN.year, TESTPLAN.term, SCORES.STUDENTNO,
               TESTPLAN.FLAG, STUDENTS.NAME, students.school
         HAVING (COUNT(*) >= 2)) sxj ON SCORES.YEAR = sxj.year AND
      SCORES.TERM = sxj.term AND SCORES.STUDENTNO = sxj.STUDENTNO INNER JOIN
      TESTPLAN ON SCORES.YEAR = TESTPLAN.year AND
      SCORES.TERM = TESTPLAN.term AND sxj.FLAG = TESTPLAN.FLAG AND
      SCORES.COURSENO + SCORES.[group] = TESTPLAN.COURSeNO INNER JOIN
      [L_考试时间表] ON TESTPLAN.year = [L_考试时间表].YEAR AND
      TESTPLAN.term = [L_考试时间表].TERM AND
      TESTPLAN.FLAG = [L_考试时间表].uid INNER JOIN
      COURSES ON SCORES.COURSENO = COURSES.COURSENO
where testplan.year=:YTWO and testplan.term=:TTWO and sxj.school like :CLASSSCHOOL and courses.school like :COURSESCHOOL and scores.studentno like :STUDENTNO
) as b