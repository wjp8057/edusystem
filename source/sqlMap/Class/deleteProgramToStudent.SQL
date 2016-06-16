delete from r28
from r28 inner join students on students.studentno=r28.studentno
where r28.programno=:PROGRAMNO AND students.CLASSNO=:CLASSNO