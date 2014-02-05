#!/usr/bin/python
"""An example of the Service Account OAuth flow.

An example of the Service Account OAuth flow for the Google Maps Engine API
used to upload raster data via the old /create_tt API endpoint.
"""

import json
import httplib2
import mimetypes
import urllib

from oauth2client.client import SignedJwtAssertionCredentials

# uncomment to get lots of useful debug output
#httplib2.debuglevel=4

# tries to return the mimetype of the file
def get_content_type(filename):
    return mimetypes.guess_type(filename)[0] or 'application/octet-stream'

def encode_multipart_formdata(filename,content):
    """
    files is a sequence of (name, filename, value) elements for data to be uploaded as files
    Return (content_type, body) ready for httplib.HTTP instance
    """
    BOUNDARY = '----------bound@ry_$'
    CRLF = '\r\n'
    L = []
    L.append('--' + BOUNDARY)
    L.append('Content-Disposition: form-data; name="%s"; filename="%s"' % (filename, filename))
    L.append('Content-Type: %s' % get_content_type(filename))
    L.append('')
    L.append(content)
    L.append('--' + BOUNDARY + '--')
    L.append('')
    body = CRLF.join(L)
    content_type = 'multipart/form-data; boundary=%s' % BOUNDARY
    return content_type, body

# need http object, filename, url
def uploadImage(id, filename, http):
  print "Trying to upload", filename
  # get contents
  with open(filename, 'r') as content_file:
      content = content_file.read()
  # now we upload the image!
  content_type, body = encode_multipart_formdata(filename, content)
  headers = {'Content-type': content_type}
  uri = 'https://www.googleapis.com/upload/mapsengine/create_tt/rasters/' + id +'/files?filename=' + filename
  response, content = http.request(uri, "POST", headers=headers, body=bytearray(body))

def main():
    # Load the key in PKCS 12 format that you downloaded from the Google API
    # Console when you created your Service account.
    with file('YOUR-KEY-HERE-privatekey.p12', 'rb') as key_file:
        key = key_file.read()

    # Create an httplib2.Http object to handle our HTTP requests and to
    # authorize them correctly.
    #
    # Note that the first parameter, service_account_name, is the Email address
    # created for the Service account. It must be the email address associated
    # with the key that was created.
    credentials = SignedJwtAssertionCredentials(
        'YOUR-SERVICE-ACCOUNT-EMAIL',
        key,
        scope='https://www.googleapis.com/auth/mapsengine')
    http = httplib2.Http()
    http = credentials.authorize(http)

    # construct the JSON packet
    json_data = {'name': 'perth',
                 'files': [{'filename':'perth1.jpg'},
                           {'filename': 'perth2.jpg'}
                          ],
                 'acquisitionTime': '1979-01-01',
                 'attribution': 'Copyright Spatial Data API Testing',
                 'sharedAccessList': 'Map Editors'
                 }

    # Read the first page of features in a Table.
    headers = {'Content-type': 'application/json'}
    uri = 'https://www.googleapis.com/mapsengine/create_tt/rasters/upload?projectId=10258059232491603613'

    response, content = http.request(uri, "POST", headers=headers, body=json.dumps(json_data))
    resource = json.loads(content.decode('utf-8'))
    if response.status == 200:
        print "Asset created with id: ", resource['id']
        for i in json_data['files']:
          filename = i['filename']
          uploadImage(resource['id'], filename, http)
    else:
        print('Error: ', resource['error'])

if __name__ == '__main__':
    main()