package com.sipanduberadat.user.services.apis

import android.content.Context
import android.view.View
import com.android.volley.Request
import com.google.gson.Gson
import com.sipanduberadat.user.models.Token
import com.sipanduberadat.user.services.FileDataPart
import com.sipanduberadat.user.services.VolleyMultipartRequest
import com.sipanduberadat.user.utils.Constants
import com.sipanduberadat.user.utils.getResponseData

fun loginAPI(contextView: View, context: Context, requestParams: HashMap<String, String>,
                 fileRequestParams: HashMap<String, FileDataPart>, callback: (Any?) -> Unit,
             errorCallback: () -> Unit, showMessage: Boolean = true) {
    VolleyMultipartRequest(context, Request.Method.POST,
        "${Constants.BASE_URL}/masyarakat/login/", requestParams, fileRequestParams, {
            val dataString = getResponseData(it, contextView, context, requestParams,
                fileRequestParams, callback, errorCallback, ::loginAPI, showMessage)

            if (dataString != null) {
                val token: Token = Gson().fromJson(dataString, Token::class.java)
                callback(token)
            }
        }, { errorCallback() }).start()
}