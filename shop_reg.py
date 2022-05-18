#!/Users/w/opt/anaconda3/bin/python3

print("Content-type:text/html")
print()

import cgi 
import re
from xml.dom.minidom import TypeInfo
import pymysql
import logging

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
    addr='nav.php' if is_succ else 'nav.php'
    print("<script>")
    print('window.location.href="%s";'%addr)
    print('window.alert("%s")'%msg)
    print("</script>")


form = cgi.FieldStorage() 
shop_name = form.getvalue('shopname')
shop_category = form.getvalue('shopcategory')
lat = form.getvalue('latitude')
lon = form.getvalue('longitude')
UID = form.getvalue('UID')

#print("shop_name = ", shop_name)
#print("shop_category = ", shop_category)
#print("lat = ", lat)
#print("lon = ", lon)
#print("UID = ", UID)

valid=0

try:
    if shop_name==None:
        assert False, 'Shop name is empty.'
    if shop_category==None:
        assert False, 'Shop category is empty.'
    if lat==None:
        assert False, 'Latitude is empty.' 
    if lon==None:
        assert False, 'Longitude is empty.'
except AssertionError as msg:
    popWindow(msg,0)

try:
    parser=re.compile("[ ]*([0-9]+[.]*[0-9]{0,6})[ ]*") 
    match=parser.fullmatch(lat)
    if match:
        lat=match.group(0)
        if float(lat)>90 or float(lat)<-90:
            assert False, 'Latitude is invalid.'
    else:
        assert False, 'Latitude is invalid.'
    
    parser=re.compile("[ ]*([0-9]+[.]*[0-9]{0,6})[ ]*") 
    match=parser.fullmatch(lon)
    if match:
        lon=match.group(0)
        if float(lon)>180 or float(lon)<-180:
            assert False, 'Longitude is invalid.'
    else:
        assert False, 'Longitude is invalid.'
    valid=1
except AssertionError as msg:
    popWindow(msg,0)

#print("<h1>all valid</h1>")

if valid:
    db=connectDb('test') 
    if db is None:
        print('error')
        exit(0)

    #print("<h1>dbconnected</h1>")
    cursor=db.cursor()
    #print("<h1>try executed sql</h1>")
    cursor.execute("SELECT name FROM store WHERE name= %(shop_name)s", {'shop_name':shop_name})
    print("<h1>executed sql succes</h1>")

    rlt = cursor.fetchone()

    if rlt!=None:
        msg='The shop name has been registered.'
        popWindow(msg,0)
    else:
        cursor = db.cursor()
        cursor.execute("UPDATE user SET role = TRUE WHERE UID = %(UID)s", {'UID':UID})

        sql="""
        INSERT INTO store
        (UID, name, category, location)
        VALUES (%(UID)s, %(shop_name)s, %(shop_category)s, ST_GeomFromText('POINT(0 0)'))
        """
        cursor.execute(sql, {'UID':UID, 'shop_name':shop_name, 'shop_category':shop_category})

        sql="""
        UPDATE store
        SET location=ST_GeomFromText('POINT(%s %s)')
        WHERE UID=%s
        """%(lon, lat, UID)
        cursor.execute(sql)

        db.commit()
        
        popWindow('Register Success.',1)
        
    db.close()