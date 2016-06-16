update students
set classno=''
where RTRIM(studentno)=:STUDENTNO
