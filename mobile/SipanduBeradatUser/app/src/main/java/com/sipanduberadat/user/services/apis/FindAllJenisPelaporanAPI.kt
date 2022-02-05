package com.sipanduberadat.user.services.apis

import android.content.Context
import android.view.View
import com.android.volley.Request
import com.google.gson.Gson
import com.sipanduberadat.user.models.JenisPelaporan
import com.sipanduberadat.user.services.FileDataPart
import com.sipanduberadat.user.services.VolleyMultipartRequest
import com.sipanduberadat.user.utils.Constants
import com.sipanduberadat.user.utils.getResponseData

fun findAllJenisPelaporanAPI(contextView: View, context: Context, requestParams: HashMap<String, String>,
                     fileRequestParams: HashMap<String, FileDataPart>, callback: (Any?) -> Unit,
                     errorCallback: () -> Unit, showMessage: Boolean = true) {
    VolleyMultipartRequest(context, Request.Method.GET,
            "${Constants.BASE_URL}/jenis-pelaporan/?active_status=true", requestParams,
            fileRequestParams, {
                val dataString = getResponseData(it, contextView, context, requestParams,
                        fileRequestParams, callback, errorCallback, ::findAllJenisPelaporanAPI, showMessage)

                if (dataString != null) {
                    val jenisPelaporan = Gson().fromJson(dataString, Array<JenisPelaporan>::class.java)
                    callback(jenisPelaporan)
                }
            }, { errorCallback() }).start()
}