package com.sipanduberadat.petugas.services.apis

import android.content.Context
import android.content.Intent
import android.view.View
import com.android.volley.Request
import com.google.gson.GsonBuilder
import com.sipanduberadat.petugas.activities.LoginActivity
import com.sipanduberadat.petugas.models.Pelaporan
import com.sipanduberadat.petugas.services.FileDataPart
import com.sipanduberadat.petugas.services.VolleyMultipartRequest
import com.sipanduberadat.petugas.utils.Constants
import com.sipanduberadat.petugas.utils.getResponseData

fun findAllEmergencyReportAPI(contextView: View, context: Context, requestParams: HashMap<String, String>,
                              fileRequestParams: HashMap<String, FileDataPart>, callback: (Any?) -> Unit,
                              errorCallback: () -> Unit, showMessage: Boolean = true) {
    val sharedPreferences = context.getSharedPreferences("SESSIONS", Context.MODE_PRIVATE)
    val accessToken = sharedPreferences.getString("ACCESS_TOKEN", null)

    if (accessToken != null) {
        val params: MutableList<String> = mutableListOf()
        requestParams["XAT"] = "Bearer $accessToken"

        if (requestParams.containsKey("id_instansi")) {
            params.add("id_instansi=${requestParams["id_instansi"]}")
            requestParams.remove("id_instansi")
        }
        val queryString = params.joinToString(separator = "&")

        VolleyMultipartRequest(context, Request.Method.GET,
                "${Constants.BASE_URL}/pelaporan-darurat/?$queryString", requestParams,
                fileRequestParams, {
                    val dataString = getResponseData(it, contextView, context, requestParams,
                            fileRequestParams, callback, errorCallback, ::findAllEmergencyReportAPI, showMessage)

                    if (dataString != null) {
                        val gson = GsonBuilder().setDateFormat("yyyy-MM-dd' 'HH:mm:ss").create()
                        val report = gson.fromJson(dataString, Array<Pelaporan>::class.java)
                        callback(report)
                    }
                }, { errorCallback() }).start()
        return
    }

    val intent = Intent(context, LoginActivity::class.java)
    intent.flags = Intent.FLAG_ACTIVITY_CLEAR_TASK or Intent.FLAG_ACTIVITY_NEW_TASK
    context.startActivity(intent)
}