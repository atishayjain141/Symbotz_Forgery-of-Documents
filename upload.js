const { google } = require('googleapis');
const path = require('path')
const fs = require('fs');
const { error } = require('console');


const clint_id = "1027429316569-0uo6e88kqmp75uvr7op4594cncvlk3q5.apps.googleusercontent.com";
const client_sec = "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";
const redirect_uri = "https://developers.google.com/oauthplayground";
const refersh_token = "1//045ZO1BYfBLu0CgYIARAAGAQSNwF-L9IrrLt20pDOPRogYjORicx4ipQb5m008ztdhK8mLNxa4ADWCh8SBj3mtMaZdBUehvt2DJ8";

const oauth2Client = new google.auth.OAuth2(
    clint_id,
    client_sec,
    redirect_uri
);

oauth2Client.setCredentials({ refresh_token: refersh_token })

const drive = google.drive(
    {
        version: 'v3',
        auth: oauth2Client

    }
)
const filePath = path.join(__dirname, '3d1.png')

async function uploadfile() {
    try {
        const response = await drive.files.create(
            {
                requestBody: {
                    name: '3d1.png',
                    mimeType: 'image/png',
                },
                media: {
                    mimeType: 'image/png',
                    body: fs.createReadStream(filePath)

                },
            }
        );
        console.log(response.data.id);

        //link
        const fileId = response.data.id;
        await drive.permissions.create({
            fileId: fileId,
            requestBody: {
                role: 'reader',
                type: 'anyone'
            }
        })
        const result = await drive.files.get({
            fileId: fileId,
            fields: 'webViewLink'
        });
        console.log(result.data.webViewLink);

    }
    catch (error) {
        console.error(error);
    }

}

async function genPublicUrl() {
    try {
        const fileId = response.data.id;
        await drive.permissions.create({
            fileId: fileId,
            requestBody: {
                role: 'reader',
                type: 'anyone'
            }
        })
        const result = await drive.files.get({
            fileId: fileId,
            fields: 'webViewLink, webContentLink'
        });
        console.log(result.data);

    }
    catch (error) {
        console.log(error)
    }
}

uploadfile();
//genPublicUrl

