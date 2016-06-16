select isnull(sum(addcredit.credit),0) as credit
from students left outer join addcredit on students.studentno=addcredit.studentno  where students.studentno=:studentno
