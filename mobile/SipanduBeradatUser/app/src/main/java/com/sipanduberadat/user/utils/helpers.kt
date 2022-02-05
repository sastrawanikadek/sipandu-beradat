package com.sipanduberadat.user.utils

import android.annotation.SuppressLint
import android.app.Activity
import android.content.Context
import android.content.Intent
import android.content.pm.PackageManager
import android.content.res.Resources
import android.location.Location
import android.location.LocationListener
import android.location.LocationManager
import android.os.Bundle
import android.provider.MediaStore
import android.util.DisplayMetrics
import android.util.Log
import android.util.TypedValue
import android.view.View
import androidx.annotation.AttrRes
import androidx.annotation.ColorInt
import androidx.core.content.ContextCompat
import com.android.volley.NetworkResponse
import com.android.volley.toolbox.HttpHeaderParser
import com.google.android.gms.location.FusedLocationProviderClient
import com.google.android.material.snackbar.Snackbar
import com.sipanduberadat.user.R
import com.sipanduberadat.user.services.FileDataPart
import com.sipanduberadat.user.services.apis.refreshTokenAPI
import org.json.JSONObject
import java.util.*
import kotlin.collections.HashMap
import kotlin.math.roundToLong

@ColorInt
fun Context.getColorFromAttr(
    @AttrRes attrColor: Int,
    typedValue: TypedValue = TypedValue(),
    resolveRefs: Boolean = true
): Int {
    theme.resolveAttribute(attrColor, typedValue, resolveRefs)
    return typedValue.data
}

@Suppress("DEPRECATION")
fun getViewport(activity: Activity): DisplayMetrics {
    val displayMetrics = DisplayMetrics()

    if (android.os.Build.VERSION.SDK_INT >= android.os.Build.VERSION_CODES.R) {
        activity.display?.getRealMetrics(displayMetrics)
    } else {
        activity.windowManager.defaultDisplay.getMetrics(displayMetrics)
    }

    return displayMetrics
}

fun Int.toDp(): Int = (this / Resources.getSystem().displayMetrics.density).toInt()
fun Int.toPx(): Int = (this * Resources.getSystem().displayMetrics.density).toInt()

@SuppressLint("DefaultLocale")
fun String.capitalizeEachWord(): String = split(" ").joinToString(" ") { it.capitalize() }

fun String.subword(delimiter: String, separator: String, total: Int = -1): String {
    val tokens = split(delimiter)

    if (tokens.size < total || total == -1) {
        return tokens.joinToString(separator)
    }

    return tokens.subList(0, total).joinToString(separator)
}

fun snackbarSuccess(
    contextView: View,
    message: String,
    length: Int
): Snackbar {
    return Snackbar.make(contextView, message, length).apply {
        setBackgroundTint(ContextCompat.getColor(context, R.color.green))
        setTextColor(ContextCompat.getColor(context, R.color.white))
    }
}

fun snackbarWarning(
    contextView: View,
    message: String,
    length: Int
): Snackbar {
    return Snackbar.make(contextView, message, length).apply {
        setBackgroundTint(ContextCompat.getColor(context, R.color.yellow))
        setTextColor(ContextCompat.getColor(context, R.color.white))
    }
}

fun snackbarError(
        contextView: View,
        message: String,
        length: Int
): Snackbar {
    return Snackbar.make(contextView, message, length).apply {
        setBackgroundTint(ContextCompat.getColor(context, R.color.red_700))
        setTextColor(ContextCompat.getColor(context, R.color.white))
    }
}

fun getRelativeDateTimeString(millis: Long): String {
    val now = Calendar.getInstance().timeInMillis - millis
    val second = (now / 1000).toFloat().roundToLong()
    val minute = (second / 60).toFloat().roundToLong()
    val hour: Long
    val day: Long
    val month: Long
    val year: Long
    val result: String

    if (minute < 1) {
        result = "Baru saja"
    } else {
        hour = (minute / 60).toFloat().roundToLong()

        if (hour < 1) {
            result = "$minute menit yang lalu"
        } else {
            day = (hour / 24).toFloat().roundToLong()

            if (day < 1) {
                result = "$hour jam yang lalu"
            } else {
                month = (day / 30).toFloat().roundToLong()

                if (month < 1) {
                    result = "$day hari yang lalu"
                } else {
                    year = (month / 12).toFloat().roundToLong()

                    result = if (year < 1) "$month bulan yang lalu" else "$year tahun yang lalu"
                }
            }
        }
    }

    return result
}

fun getTimeCountdown(startTime: Long, endTime: Long, timeFormat: Boolean = false): String {
    val diff = endTime - startTime
    val days = diff / (1000 * 60 * 60 * 24)
    val hours = diff / (1000 * 60 * 60) % 24
    val minutes = diff / (1000 * 60) % 60
    val seconds = diff / 1000 % 60

    return if (timeFormat) String.format("%02d:%02d:%02d", hours, minutes, seconds) else
        String.format("%02d hari, %02d jam, %02d menit, %02d detik", days, hours, minutes, seconds)
}

fun getDate(date: Date, withMonthName: Boolean = true, yearFirst: Boolean = false): String {
    val calendar = Calendar.getInstance().apply { time = date }
    val delimiter = if (withMonthName) ' ' else '-'
    val year = calendar[Calendar.YEAR]
    val month = if (withMonthName) Constants.MONTH_NAMES[calendar[Calendar.MONTH]] else
        (calendar[Calendar.MONTH] + 1).toString().padStart(2, '0')
    val dayOfMonth = calendar[Calendar.DATE].toString().padStart(2, '0')

    return if (yearFirst) "$year$delimiter$month$delimiter$dayOfMonth" else
        "$dayOfMonth$delimiter$month$delimiter$year"
}

fun getDateTime(date: Date, withMonthName: Boolean = true, withSecond: Boolean = true,
                withLocale: Boolean = true, yearFirst: Boolean = false): String {
    val calendar = Calendar.getInstance().apply { time = date }
    val delimiter = if (withMonthName) ' ' else '-'
    val year = calendar[Calendar.YEAR]
    val month = if (withMonthName) Constants.MONTH_NAMES[calendar[Calendar.MONTH]] else
        (calendar[Calendar.MONTH] + 1).toString().padStart(2, '0')
    val dayOfMonth = calendar[Calendar.DATE].toString().padStart(2, '0')
    val hour = calendar[Calendar.HOUR_OF_DAY].toString().padStart(2, '0')
    val minute = calendar[Calendar.MINUTE].toString().padStart(2, '0')
    val second = calendar[Calendar.SECOND].toString().padStart(2, '0')
    val locale = if (withLocale) " WITA" else ""

    return if (yearFirst) "$year$delimiter$month$delimiter$dayOfMonth $hour:$minute" +
            "${if (withSecond) ":$second" else ""}$locale" else
                "$dayOfMonth$delimiter$month$delimiter$year $hour:$minute" +
                        "${if (withSecond) ":$second" else ""}$locale"
}

@Suppress("NULLABILITY_MISMATCH_BASED_ON_JAVA_ANNOTATIONS")
@SuppressLint("VisibleForTests", "MissingPermission")
fun requestLocation(ctx: Context, callback: (location: Location) -> Unit) {
    val fusedLocationProviderClient = FusedLocationProviderClient(ctx)

    fusedLocationProviderClient.lastLocation.addOnSuccessListener {
        if (it != null) {
            callback(it)
        } else {
            val locationManager: LocationManager = ctx.getSystemService(Context.LOCATION_SERVICE)
                    as LocationManager
            locationManager.requestLocationUpdates(LocationManager.GPS_PROVIDER, 0,
                    0f, object: LocationListener {
                override fun onLocationChanged(location: Location) {
                    callback(location)
                    locationManager.removeUpdates(this)
                }

                override fun onStatusChanged(provider: String?, status: Int, extras: Bundle?) {}
            })
        }
    }
}

fun checkPermissions(
    context: Context,
    permissions: Array<String>
): Boolean {
    for (permission in permissions) {
        if (ContextCompat.checkSelfPermission(context, permission) != PackageManager.PERMISSION_GRANTED) {
            return false
        }
    }

    return true
}

fun choosePhoto(): Intent {
    val galleryIntent = Intent(Intent.ACTION_PICK, MediaStore.Images.Media.INTERNAL_CONTENT_URI)
    val cameraIntent = Intent(MediaStore.ACTION_IMAGE_CAPTURE)
    val intentChooser = Intent.createChooser(galleryIntent, "Pilih sumber foto")
    intentChooser.putExtra(Intent.EXTRA_INITIAL_INTENTS, arrayOf(cameraIntent))
    return intentChooser
}

fun getResponseData(response: NetworkResponse, contextView: View, context: Context,
                    requestParams: HashMap<String, String>,
                    fileRequestParams: HashMap<String, FileDataPart>,
                    successCallback: (Any?) -> Unit,
                    errorCallback: () -> Unit,
                    callback: (View, Context, HashMap<String, String>, HashMap<String, FileDataPart>,
                               (Any?) -> Unit, () -> Unit, Boolean) -> Unit,
                    showMessage: Boolean = true)
        : String? {
    val json = String(response.data, charset(HttpHeaderParser.parseCharset(response.headers)))
    Log.e("JSON", json)
    val responseObject = JSONObject(json)
    val message = responseObject.getString("message").capitalizeEachWord()

    when (responseObject.getInt("status_code")) {
        200 -> {
            if (showMessage) snackbarSuccess(contextView, message, Snackbar.LENGTH_LONG).show()
            return responseObject.getString("data")
        }
        400 -> {
            if (showMessage) snackbarWarning(contextView, message, Snackbar.LENGTH_LONG).show()
            errorCallback()
        }
        401 -> {
            refreshTokenAPI(contextView, context, requestParams, fileRequestParams,
                    successCallback, errorCallback, callback, showMessage)
        }
        500 -> {
            if (showMessage) snackbarError(contextView, message, Snackbar.LENGTH_LONG).show()
            errorCallback()
        }
    }

    return null
}