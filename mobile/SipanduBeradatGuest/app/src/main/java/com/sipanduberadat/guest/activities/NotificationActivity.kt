package com.sipanduberadat.guest.activities

import androidx.appcompat.app.AppCompatActivity
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.view.View
import androidx.recyclerview.widget.LinearLayoutManager
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.adapters.NotificationAdapter
import com.sipanduberadat.guest.models.Notifikasi
import com.sipanduberadat.guest.services.apis.findAllNotificationAPI
import kotlinx.android.synthetic.main.activity_notification.*

class NotificationActivity : AppCompatActivity() {
    @Suppress("UNCHECKED_CAST")
    private fun onSuccessNotification(response: Any?) {
        if (response != null) {
            shimmer_container.stopShimmer()
            shimmer_container.visibility = View.GONE
            recycler_view.visibility = View.VISIBLE

            val data = (response as Array<Notifikasi>).toList()
            if (data.isEmpty()) {
                recycler_view.visibility = View.GONE
                empty_container.visibility = View.VISIBLE
            } else {
                empty_container.visibility = View.GONE
                recycler_view.visibility = View.VISIBLE
                recycler_view.adapter = NotificationAdapter(this, data)
            }
        }
    }

    private fun onRequestError() {}

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_notification)

        recycler_view.layoutManager = LinearLayoutManager(this, LinearLayoutManager.VERTICAL,
            false)

        btn_back.setOnClickListener { finish() }
        swipe_refresh.setOnRefreshListener {
            Handler(Looper.getMainLooper()).postDelayed({
                swipe_refresh.isRefreshing = false
                recycler_view.visibility = View.GONE
                shimmer_container.visibility = View.VISIBLE
                shimmer_container.startShimmer()

                findAllNotificationAPI(root, this, HashMap(), HashMap(), this::onSuccessNotification,
                    this::onRequestError, showMessage = false)
            }, 300)
        }

        findAllNotificationAPI(root, this, HashMap(), HashMap(), this::onSuccessNotification,
            this::onRequestError, showMessage = false)
    }
}