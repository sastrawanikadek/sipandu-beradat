package com.sipanduberadat.guest.services.apis

import android.content.Context
import android.content.Intent
import android.view.View
import com.android.volley.Request
import com.google.gson.GsonBuilder
import com.sipanduberadat.guest.activities.LoginActivity
import com.sipanduberadat.guest.models.Pelaporan
import com.sipanduberadat.guest.models.PelaporanTamu
import com.sipanduberadat.guest.services.FileDataPart
import com.sipanduberadat.guest.services.VolleyMultipartRequest
import com.sipanduberadat.guest.utils.Constants
import com.sipanduberadat.guest.utils.getResponseData

fun findAllTamuNotEmergencyReportAPI(contextView: View, context: Context, requestParams: HashMap<String, String>,
                                  fileRequestParams: HashMap<String, FileDataPart>, callback: (Any?) -> Unit,
                                  errorCallback: () -> Unit, showMessage: Boolean = true) {
    val sharedPreferences = context.getSharedPreferences("SESSIONS", Context.MODE_PRIVATE)
    val accessToken = sharedPreferences.getString("ACCESS_TOKEN", null)

    if (accessToken != null) {
        val params: MutableList<String> = mutableListOf()
        requestParams["XAT"] = "Bearer $accessToken"

        if (requestParams.containsKey("id_tamu")) {
            params.add("id_tamu=${requestParams["id_tamu"]}")
            requestParams.remove("id_tamu")
        }

        if (requestParams.containsKey("id_desa")) {
            params.add("id_desa=${requestParams["id_desa"]}")
            requestParams.remove("id_desa")
        }
        val queryString = params.joinToString(separator = "&")

        VolleyMultipartRequest(context, Request.Method.GET,
                "${Constants.BASE_URL}/pelaporan-tamu/?$queryString", requestParams,
                fileRequestParams, {
            val dataString = getResponseData(it, contextView, context, requestParams,
                    fileRequestParams, callback, errorCallback, ::findAllTamuNotEmergencyReportAPI, showMessage)

            if (dataString != null) {
                val gson = GsonBuilder().setDateFormat("yyyy-MM-dd' 'HH:mm:ss").create()
                val report = gson.fromJson(dataString, Array<PelaporanTamu>::class.java)
                callback(report)
            }
        }, { errorCallback() }).start()
        return
    }

    val intent = Intent(context, LoginActivity::class.java)
    intent.flags = Intent.FLAG_ACTIVITY_CLEAR_TASK or Intent.FLAG_ACTIVITY_NEW_TASK
    context.startActivity(intent)
}