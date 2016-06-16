SELECT SUM(courses.CREDITS) as XF
FROM SCORES INNER JOIN COURSES ON COURSES.COURSENO=SCORES.COURSENO
WHERE(examscore >= 60 OR
      examscore2 >= 60 OR	
      testscore= '优秀' OR
      testscore = '良好' OR
      testscore = '中等' OR
      testscore = '及格' OR
      testscore = '合格' OR
      testscore2= '优秀' OR
      testscore2 = '良好' OR
      testscore2 = '中等' OR
      testscore2 = '及格' OR
      testscore2 = '合格'
) AND 
      studentno =:STUDENTNO
