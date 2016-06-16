UPDATE TEACHERS SET SEX=:SEX,Birth=CAST(:Birth AS datetime),ID=:ID,Nationality=:Nationality,Party=:Party,Department=:Department,HeadShip=:HeadShip,
HDate= CAST(:HDate AS datetime),Profession=:Profession,PDate=CAST(:PDate AS datetime),PSubject=:PSubject,EduLevel=:EduLevel,ESchool=:ESchool,
Degree=:Degree,DSchool=:DSchool,Tel=:Tel,Email=:Email WHERE TEACHERNO = :TEACHERNO