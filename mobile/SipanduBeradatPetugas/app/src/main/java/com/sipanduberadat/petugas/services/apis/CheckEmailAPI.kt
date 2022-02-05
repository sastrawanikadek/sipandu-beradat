package com.sipanduberadat.petugas.services.apis

import android.content.Context
import android.view.View
import com.android.volley.Request
import com.sipanduberadat.petugas.services.FileDataPart
import com.sipanduberadat.petugas.services.VolleyMultipartRequest
import com.sipanduberadat.petugas.utils.Constants
import com.sipanduberadat.petugas.utils.getResponseData

fun checkEmailAPI(contextView: View, context: Context, requestParams: HashMap<String, String>,
                     fileRequestParams: HashMap<String, FileDataPart>, callback: (Any?) -> Unit,
                     errorCallback: () -> Unit, showMessage: Boolean = true) {
    if (!requestParams.containsKey("email")) return

    val email = requestParams["email"]

    VolleyMultipartRequest(context, Request.Method.GET,
            "${Constants.BASE_URL}/petugas/check-email/?email=$email", requestParams, fileRequestParams, {
        val dataString = getResponseData(it, contextView, context, requestParams,
                fileRequestParams, callback, errorCallback, ::checkEmailAPI, showMessage)

        if (dataString != null) {
            callback(dataString)
        }
    }, { errorCallback() }).start()
}