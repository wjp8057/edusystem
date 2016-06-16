if exists(select course_id from standard_class where course_id=:cone)
  begin
    update standard_class set numbers=:none,code=:code_one where course_id=:ctwo
  end
else
  begin
    insert into standard_class(course_id,courseno,The_course_type,coursename,numbers,code)
    values(:course_id,:courseno,:The_course_type,:coursename,:numbers,:code)
  end