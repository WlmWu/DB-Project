#!/Users/w/opt/anaconda3/bin/python3

print("Content-type:text/html")
print()

import cgi, os
import re
from xml.dom.minidom import TypeInfo
import pymysql
import logging
import cgitb; cgitb.enable()
from PIL import Image
import base64,io



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
    addr='nav.php'
    print("<script>")
    print('window.location.href="%s";'%addr)
    print('window.alert("%s")'%msg)
    print("</script>")

inFileData = None
form = cgi.FieldStorage() 
UPLOAD_DIR='Picture/'

SID = form.getvalue('SID')
name = form.getvalue('mealname')
pri = form.getvalue('price')
quan = form.getvalue('quantity')
img = form.getvalue('file')
imgType = form.getvalue('imgType')


# print("file: ",len(img),'<br>')



valid=0

try:
    if name==None:
        assert False, 'Item name is empty.'
    if pri==None:
        assert False, 'Price is empty.'
    if quan==None:
        assert False, 'Quantity is empty.' 
    
except AssertionError as msg:
    popWindow(msg,0)

# print("<h1>none empty</h1>")

try:
    parser=re.compile("[ ]*([1-9][0-9]*)[ ]*") 
    match=parser.fullmatch(pri)
    if match:
        pri=match.group(0)
        if int(pri)<0:
            assert False, 'Price is invalid.'
    else:
        assert False, 'Price is invalid.'
    
    parser=re.compile("[ ]*([1-9][0-9]*)[ ]*") 
    match=parser.fullmatch(quan)
    if match:
        quan=match.group(0)
        if int(quan)<0:
            assert False, 'Quantity is invalid.'
    else:
        assert False, 'Quantity is invalid.'
    valid=1
except AssertionError as msg:
    popWindow(msg,0)


# print("<img src='data:",imgType,";base64,",img,"'/>")
# uploaded_file_path = os.path.join(UPLOAD_DIR, os.path.basename(img))


if valid:
    db=connectDb('test') 
    if db is None:
        print('error')
        exit(0)

    # print("<h1>dbconnected</h1>")
    cursor=db.cursor()
    cursor.execute("SELECT name FROM product WHERE name= %s AND SID = %s", (name, SID))
    rlt = cursor.fetchone()
    if rlt!=None:
        msg='The product name has been registered.'
        popWindow(msg,0)
    else:
        cursor = db.cursor()
        sql="""
        INSERT INTO product
        (SID, name, picture, price, quantity, imgType)
        VALUES (%(SID)s, %(name)s, %(img)s, %(pri)s, %(quan)s, "%(imgType)s")
        """
        sql="""
        INSERT INTO product
        (SID, name, picture, price, quantity, imgType)
        VALUES (%s, %s, %s, %s, %s, %s)
        """

        cursor.execute(sql,[SID,name,img,pri,quan,imgType])
        # cursor.execute(sql,{'SID':SID, 'name':name, 'img':img, 'pri':pri, 'quan':quan, 'imgType':imgType})


        db.commit()
        
        popWindow('Add Success.',1)
        
    db.close()