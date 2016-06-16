insert into TestStudent(CourseNo,Studentno,Flag,Lock,CourseNo2)
select S.COURSENO,S.STUDENTNO,0,0,S.COURSENO FROM SCORES AS S WHERE S.YEAR=:YEAR AND S.TERM=:TERM
AND (S.PLANTYPE='M' OR S.PLANTYPE='T') AND S.EXAMSCORE < 60 AND S.TESTSCORE NOT IN ('及格',
      '合格', '中等', '优秀', '良好') AND (S.EXAMSCORE2 < 60 OR
      S.EXAMSCORE2 IS NULL) AND (S.TESTSCORE2 NOT IN ('及格', '合格', '中等', '优秀', '良好') OR
      S.TESTSCORE2 IS NULL) and NOT EXISTS (
          select * from TestStudent as T WHERE T.STUDENTNO=S.STUDENTNO AND T.COURSENO=S.COURSENO
      )
