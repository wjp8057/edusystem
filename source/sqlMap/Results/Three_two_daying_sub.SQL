update scores
set examscore2=:examscore,testscore2=:testscore,date2=getdate()
from scores inner join makeup on makeup.courseno=scores.courseno and scores.year=makeup.year
and scores.term=makeup.term and scores.studentno=makeup.studentno
where scores.RECNO=:recno and makeup.lock=0;
update makeup
set lock=1,edate=:edate,date=getdate()
where courseno=:courseno and year=:year and term=:term AND STUDENTNO=:studentno
