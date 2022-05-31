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
    addr='index.html' if is_succ else 'sign-up.html'
    print("<script>")
    print('window.location.href="%s";'%addr)
    print('window.alert("%s")'%msg)
    print("</script>")


form = cgi.FieldStorage() 
name = form.getvalue('name')
pho = form.getvalue('phonenumber')
acnt = form.getvalue('Account')
pwd = form.getvalue('password')
re_pwd = form.getvalue('re-password')
lat = form.getvalue('latitude')
lon = form.getvalue('longitude')

valid=0

try:
    if name==None:
        assert False, 'Name is empty.'
    if pho==None:
        assert False, 'Phone number is empty.'
    if acnt==None:
        assert False, 'Account is empty.'
    if pwd==None:
        assert False, 'Passward is empty.'
    if re_pwd==None:
        assert False, 'Re-type passward is empty.'
    if lat==None:
        assert False, 'Latitude is empty.' 
    if lon==None:
        assert False, 'Longitude is empty.'
except AssertionError as msg:
    popWindow(msg,0)

# print("<h1>none empty</h1>")

try:
    parser=re.compile("[ ]*([A-Za-z0-9]*)[ ]*")
    match=parser.fullmatch(name)
    if match:
        name=match.group(0)
    else:
        assert False, 'Name contains English alphabets and numbers only.'
    
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
        if pwd!=re_pwd:
            assert False, 'Re-type password is different.'
    else:
        assert False, 'Password contains English alphabets and numbers only.'
    
    parser=re.compile("[ ]*([09][0-9]*)[ ]*") 
    match=parser.fullmatch(pho)
    if match:
        pho=match.group(0)
        if len(pho)!=10:
            assert False, 'Phone number is invalid.'
    else:
        assert False, 'Phone number is invalid.'

    parser=re.compile("[ ]*([0-9]+[.]*[0-9]*)[ ]*") 
    match=parser.fullmatch(lat)
    if match:
        lat=match.group(0)
        if float(lat)>90 or float(lat)<-90:
            assert False, 'Latitude is invalid.'
    else:
        assert False, 'Latitude is invalid.'
    
    parser=re.compile("[ ]*([0-9]+[.]*[0-9]*)[ ]*") 
    match=parser.fullmatch(lon)
    if match:
        lon=match.group(0)
        if float(lon)>180 or float(lon)<-180:
            assert False, 'Latitude is invalid.'
    else:
        assert False, 'Longitude is invalid.'
    valid=1
except AssertionError as msg:
    popWindow(msg,0)

# print("<h1>all valid</h1>")

if valid:
    pwd=sha256(pwd.encode('utf-8')).hexdigest()

    db=connectDb('test') 
    if db is None:
        print('error')
        exit(0)

    cursor=db.cursor()
    sql="""
        select account
        from user
        where account='%s'
        """%(acnt)

    cursor.execute(sql)
    rlt = cursor.fetchone()

    if rlt!=None:
        msg='Account has been registered.'
        popWindow(msg,0)
    else:
        # sql="""
        # INSERT INTO user
        # (account, password, name, phone, longitude, latitude)
        # VALUES ('%s', '%s', '%s', '%s', %s, %s)
        # """%(acnt,pwd,name,pho,lon,lat)
        sql="""
        INSERT INTO user
        (account, password, name, phone, location, role, wallet)
        VALUES ('%s', '%s', '%s', '%s', ST_GeomFromText('POINT(%s %s)'), FALSE, 0)
        """%(acnt,pwd,name,pho,lon,lat)
        cursor.execute(sql)
        db.commit()
        popWindow('Register Success.',1)
        # print("<h1>inserted</h1>")

    db.close()

    # print("<h1>done</h1>")

