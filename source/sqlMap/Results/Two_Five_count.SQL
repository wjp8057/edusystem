select count(*) as ROWS from(SELECT row_number() over(ORDER BY classes.school, classes.classno, students.studentno) as row,STUDENTS.STUDENTNO xh, STUDENTS.NAME  xm, statusoptions.value xjzt,
      isnull(SELECTIONS.TOTALCREDITS,0)  hdxf, lsselection.xkxf xkxf,classes.classname  bj,schools.name xy
FROM STUDENTS inner join statusoptions on statusoptions.name=students.status
inner join classes on classes.classno=students.classno
inner join schools on schools.school=classes.school
LEFT OUTER JOIN
          (SELECT STUDENTNO, SUM(COURSES.CREDITS) AS TOTALCREDITS
         FROM SCORES JOIN
               COURSES ON SCORES.COURSENO = COURSES.COURSENO
         WHERE YEAR =:YONE AND TERM =:TONE AND (SCORES.EXAMSCORE >= 60 OR SCORES.EXAMSCORE2 >= 60 OR
               SCORES.TESTSCORE = '优秀' OR
               SCORES.TESTSCORE = '良好' OR
               SCORES.TESTSCORE = '及格' OR
               SCORES.TESTSCORE = '中等' OR
               SCORES.TESTSCORE = '合格' OR
               SCORES.TESTSCORE2 = '优秀' OR
               SCORES.TESTSCORE2 = '良好' OR
               SCORES.TESTSCORE2 = '及格' OR
               SCORES.TESTSCORE2 = '中等' OR
               SCORES.TESTSCORE2 = '合格' )
         AND SCORES.EXAMSCORE IS not NULL AND [GROUP] NOT IN ('BY','ZX')
         GROUP BY STUDENTNO) AS SELECTIONS ON
      STUDENTS.STUDENTNO = SELECTIONS.STUDENTNO LEFT OUTER JOIN
          (SELECT STUDENTNO, SUM(COURSES.CREDITS) AS xkxf
         FROM SCORES JOIN
               COURSES ON SCORES.COURSENO = COURSES.COURSENO
         WHERE YEAR =:YTWO AND TERM =:TTWO and examscore is not null and [group] not in ('BY','ZX')
         GROUP BY STUDENTNO) AS lsSELECTION ON
      STUDENTS.STUDENTNO = lsSELECTION.STUDENTNO
WHERE xkxf IS NOT NULL and xkxf>0 AND (totalcredits * 2 <= xkxf or totalcredits is null)and classes.school=:SCHOOL
) as b