#!/Users/w/opt/anaconda3/bin/python3

print("Content-type:text/html")
print()

import cgi 
import re
import pymysql
import logging
from hashlib import sha256

def connectDb(dbName):
    try:
        mysqldb = pymysql.connect(
                host="127.0.0.1",
                user="root",
                port=3306,
                database=dbName)
        return mysqldb
    except Exception as e:
        logging.error('Fail to connection mysql {}'.format(str(e)))
    return None


def popWindow(msg,is_succ):
    addr='nav.html' if is_succ else 'index.html'
    print("<script>")
    print('window.location.href="%s";'%addr)
    print('window.alert("%s")'%msg)
    print("</script>")


form = cgi.FieldStorage() 
acnt = form.getvalue('Account')
pwd = form.getvalue('password')
# print("acnt:",acnt)

try:
    if acnt==None:
        assert False, 'Account is empty.'
    if pwd==None:
        assert False, 'Passward is empty.'
except AssertionError as msg:
    popWindow(msg,0)

# print("<h1>none empty</h1>")

try:
    parser=re.compile("[ ]*([A-Za-z0-9]*)[ ]*")
    match=parser.fullmatch(acnt)
    if match:
        acnt=match.group(0)
    else:
        assert False, 'Account contains English alphabets and numbers only.'
    
    parser=re.compile("([A-Za-z0-9]*)")    
    match=parser.fullmatch(pwd)
    if match:
        pwd=match.group(0)
    else:
        assert False, 'Password contains English alphabets and numbers only.'
    
except AssertionError as msg:
    popWindow(msg,0)

pwd=sha256(pwd.encode('utf-8')).hexdigest()

db=connectDb('test') 
if db is None:
    print('error')
    exit(0)

cursor=db.cursor()
sql="""
    select *
    from user
    where account='%s'
    """%(acnt)

cursor.execute(sql)
rlt = cursor.fetchone()


if rlt==None:
    msg="Account hasn't been registered."
    popWindow(msg,0)
else:
    fthAcnt=rlt[1]
    fthPwd=rlt[2]
    # fthName=rlt[3]
    # fthPho=rlt[4]
    # fthLon=rlt[5]
    # fthLat=rlt[6]
    if fthPwd!=pwd:
        msg="Incorrect password"
        popWindow(msg,0)
    else:
        # print('Login success')
        # userdata = {"account": fthAcnt, "password": fthPwd, "name": fthName, "phone":fthPho, "longitude":fthLon, "latitude":fthLat}
        userdata={"account":fthAcnt}
        addr='nav.php'
        print('<form action="%s" method="post">'%addr)
        for k,v in userdata.items():
            # print(k,v,'<br>')
            print(f"<input type='hidden' name='{k}' value='{v}'>")
        print('</form>')
        print("<script>")
        print("document.getElementsByTagName('form')[0].submit()")
        print("</script>")
        
db.close()