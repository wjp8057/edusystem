select count(ProgramNo) As Rows from Programs
where ProgramNo like :PROGRAMNO and ProgName like :PROGRAMNAME
and School like :SCHOOL
