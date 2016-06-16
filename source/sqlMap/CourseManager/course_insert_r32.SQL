DECLARE @year char(4) DECLARE @term char(1)
SET @year=:year
SET @term=:term
INSERT INTO r32(YEAR,term,courseno,[group],studentno,coursetype,examtype,selecttime)
SELECT DISTINCT courseplan.YEAR,courseplan.term,
                                courseplan.courseno,
                                courseplan.[group],
                                students.studentno,
                                courseplan.coursetype,
                                courseplan.examtype ,
                                getdate() s
FROM students
INNER JOIN courseplan ON courseplan.classno=students.classno
WHERE courseplan.coursetype = :type
  AND courseplan.YEAR=@year
  AND courseplan.term=@term
  AND students.status='A'
  AND NOT EXISTS
    (SELECT *
     FROM r32
     WHERE r32.YEAR=@year
       AND r32.term=@term
       AND r32.courseno=courseplan.courseno
--        AND r32.[group]=courseplan.[group]
       AND students.studentno=r32.studentno)