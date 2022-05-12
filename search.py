#!/Users/w/opt/anaconda3/bin/python3

print("Content-type:text/html")
print()

import cgi 
import re
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

form = cgi.FieldStorage() 
usrLoc=[form.getvalue('longitude'),form.getvalue('latitude')]
name=form.getvalue('shopName')
dis=form.getvalue('dist')
pLw=form.getvalue('PriLow')
pHi=form.getvalue('PriHigh')
meal=form.getvalue('Meal')
cat=form.getvalue('categ')

if dis=='near':
    dis='3000'
elif dis=='medium':
    dis='6000'
else:
    dis='10000'

db=connectDb('test') 
if db is None:
    print('error')
    exit(0)

cursor=db.cursor()

# distance
sql="""
    SELECT * , ST_AsText(location) AS storeLoc, ST_Distance_Sphere(POINT(%s,%s),location) AS distant
    FROM store
    WHERE ST_Distance_Sphere(POINT(%s,%s),location) < %s ORDER BY distant
    """%(usrLoc[0],usrLoc[1],usrLoc[0],usrLoc[1],dis)

# sql="""
#     SELECT * , ST_AsText(location) AS storeLoc, ST_Distance_Sphere(POINT(%s,%s),location) AS distant
#     FROM store
#     """%(usrLoc[0],usrLoc[1])

cursor.execute(sql)
rlt = cursor.fetchall()     # row: (SID, UID, name, categ, loca, txtLoc, dis)
stores=[]

for row in rlt:
    tmp={}
    tmp['SID']=row[0]
    tmp['name']=row[2]
    tmp['categ']=row[3]
    stores.append(tmp)

tmp=[]
for s in stores:
    sql="""
        SELECT SID
        FROM product
        WHERE SID='%s '
        """%(s['SID'])
    cursor.execute(sql)
    rlt = cursor.fetchall()
    if rlt==():         # store is empty
        continue
    tmp.append(s)
stores=tmp

# name
if name!=None:
    tmp=[]
    for s in stores:
        mth = re.search(name.lower(), s['name'].lower())
        if mth!=None:
            tmp.append(s)
    stores=tmp

# price
if pLw!=None or pHi!=None:
    tmp=[]
    for s in stores:
        sql="""
            SELECT SID, price
            FROM product
            WHERE SID='%s '
            """%(s['SID'])
        if pLw!=None and pHi!=None:
            sql+=" AND (price<%s OR price>%s) "%(pLw,pHi)
        else:
            if pLw!=None:
                sql+=" AND price<%s "%pLw
            if pHi!=None:
                sql+=" AND price>%s "%pHi
        cursor.execute(sql)
        rlt = cursor.fetchall()
        if rlt==():
            tmp.append(s)
    stores=tmp

# meal
if meal!=None:
    tmp=[]
    for s in stores:
        sql="""
            SELECT SID, name
            FROM product
            WHERE SID='%s '
            """%(s['SID'])
        cursor.execute(sql)
        rlt = cursor.fetchall()
        for row in rlt:
            print(row)
            mth = re.search(meal.lower(), row[1].lower())
            if mth!=None:
                tmp.append(s)
                break
    stores=tmp

# category
if cat!=None:
    tmp=[]
    for s in stores:
        mth = re.search(cat.lower(), s['categ'].lower())
        if mth!=None:
            tmp.append(s)
    stores=tmp

# print(stores)
addr='nav.php'
print('<form action="%s" method="post">'%addr)
for s in stores:
    print(f"<input type='hidden' name='srhShopId[]' value='{s['SID']}'>")
    print(f"<input type='hidden' name='srhShopName[]' value='{s['name']}'>")
    print(f"<input type='hidden' name='srhShopCat[]' value='{s['categ']}'>")
print(f"<input type='hidden' name='shopDis' value='{form.getvalue('dist')}'>")
print('</form>')
print("<script>")
print("document.getElementsByTagName('form')[0].submit()")
print("</script>")
