<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Title Mail</title>
</head>
<body style="font-family: Arial, sans-serif; background:#f5f5f5; padding:30px;">

    <div style="max-width:600px; margin:auto; background:white; padding:30px; border-radius:12px;">

        <h2 style="margin-top:0;">
            Support Ticket Submitted
        </h2>

        <p>
            Your support ticket has been submitted successfully.
        </p>

       <p>Here are the details of your ticket:</p>

        <table width="100%" cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse;">

            <tr>
                <td><strong>Ticket Number</strong></td>
                <td> id_ticket </td>
            </tr>

            <tr>
                <td><strong>Request Date</strong></td>
                <td> created_at->format('d F Y') </td>
            </tr>

            <tr>
                <td><strong>Requester</strong></td>
                <td> requester_name </td>
            </tr>

            <tr>
                <td><strong>Email</strong></td>
                <td> requester_email </td>
            </tr>

            <tr>
                <td><strong>Phone Number</strong></td>
                <td> phone_number </td>
            </tr>

            <tr>
                <td><strong>Partner Team</strong></td>
                <td> partner_name </td>
            </tr>

            <tr>
                <td><strong>Description</strong></td>
                <td> issue_description </td>
            </tr>

            <tr>
                <td><strong>Status</strong></td>
                <td> strtoupper(status) </td>
            </tr>

        </table>

        <div style="margin-top:30px; text-align:center;">

            <a
                href=" url('/') "
                style="
                    background:#2563eb;
                    color:white;
                    padding:12px 20px;
                    border-radius:8px;
                    text-decoration:none;
                    display:inline-block;
                "
            >
                Check Ticket Status
            </a>

        </div>

        <p style="margin-top:30px; font-size:13px; color:#666;">
            Please save your ticket number for tracking purposes.
        </p>

    </div>

</body>
</html>