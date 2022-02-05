package com.sipanduberadat.user.fragments

import android.annotation.SuppressLint
import android.app.Activity
import android.content.Context
import android.content.Intent
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.core.content.ContextCompat
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import androidx.recyclerview.widget.LinearLayoutManager
import com.bumptech.glide.Glide
import com.google.android.flexbox.FlexDirection
import com.google.android.flexbox.FlexWrap
import com.google.android.flexbox.FlexboxLayoutManager
import com.google.android.flexbox.JustifyContent
import com.google.android.material.badge.BadgeDrawable
import com.google.android.material.badge.BadgeUtils
import com.google.android.material.dialog.MaterialAlertDialogBuilder
import com.sipanduberadat.user.R
import com.sipanduberadat.user.activities.AddReportActivity
import com.sipanduberadat.user.activities.NotificationActivity
import com.sipanduberadat.user.activities.ReportHistoryActivity
import com.sipanduberadat.user.adapters.EmergencyAdapter
import com.sipanduberadat.user.adapters.HistoryAdapter
import com.sipanduberadat.user.utils.subword
import com.sipanduberadat.user.viewModels.MainViewModel
import kotlinx.android.synthetic.main.layout_main_home.view.*

class MainHomeFragment: Fragment() {
    private lateinit var viewModel: MainViewModel
    private var badgeDrawable: BadgeDrawable? = null

    @SuppressLint("UnsafeExperimentalUsageError")
    private fun onInitNotification(v: View) {
        val sharedPreferences = v.context.getSharedPreferences("PREFERENCES", Context.MODE_PRIVATE)
        val notificationCounter: Int = sharedPreferences.getInt("NOTIFICATION_COUNT", 0)

        BadgeUtils.detachBadgeDrawable(badgeDrawable, v.btn_notification)
        if (notificationCounter > 0) {
            badgeDrawable!!.number = notificationCounter
            BadgeUtils.attachBadgeDrawable(badgeDrawable!!, v.btn_notification)
        }
    }

    override fun onCreateView(
        inflater: LayoutInflater,
        container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {
        val view = inflater.inflate(R.layout.layout_main_home, container, false)
        viewModel = ViewModelProvider(activity!!).get(MainViewModel::class.java)
        badgeDrawable = BadgeDrawable.create(view!!.context).apply {
            horizontalOffset = 30
            verticalOffset = 20
            backgroundColor = ContextCompat.getColor(view.context, R.color.red_700)
            badgeTextColor = ContextCompat.getColor(view.context, R.color.white)
        }

        val flexboxLayoutManager = FlexboxLayoutManager(view.context, FlexDirection.ROW, FlexWrap.WRAP)
        flexboxLayoutManager.justifyContent = JustifyContent.SPACE_BETWEEN

        view.emergency_recycler_view.apply {
            layoutManager = flexboxLayoutManager
            adapter = EmergencyAdapter(view.context, if (viewModel.reportTypes.value != null)
                viewModel.reportTypes.value!!.filter { it.emergency_status } else listOf(), viewModel)
        }

        view.history_recycler_view.layoutManager = LinearLayoutManager(view.context,
                LinearLayoutManager.VERTICAL, false)

        view.btn_report.setOnClickListener {
            if (!viewModel.me.value!!.masyarakat.valid_status) {
                MaterialAlertDialogBuilder(view.context)
                        .setTitle("Akun belum tervalidasi")
                        .setMessage("Mohon minta validasi terlebih dahulu dari admin di desa adat " +
                                "agar dapat mengajukan pelaporan")
                        .setPositiveButton("Tutup") { dialog, _ ->
                            dialog.dismiss()
                        }
                        .show()
                return@setOnClickListener
            } else if (viewModel.me.value!!.masyarakat.block_status) {
                MaterialAlertDialogBuilder(view.context)
                        .setTitle("Akun Terblokir")
                        .setMessage("Akun Anda telah terblokir karena pelaporan tidak valid. " +
                                "Mohon minta admin di desa adat untuk membuka blokirnya " +
                                "agar dapat mengajukan pelaporan")
                        .setPositiveButton("Tutup") { dialog, _ ->
                            dialog.dismiss()
                        }
                        .show()
                return@setOnClickListener
            }

            val intent = Intent(view.context, AddReportActivity::class.java)
            startActivityForResult(intent, 1)
        }

        viewModel.me.observe(activity!!, {
            if (it != null) {
                view.top_shimmer_container.stopShimmer()
                view.top_shimmer_container.visibility = View.GONE
                view.top_container.visibility = View.VISIBLE

                view.name.text = it.masyarakat.name.subword(" ", " ", 4)
                Glide.with(view.context).load(it.masyarakat.avatar).centerCrop().into(view.avatar)
                view.btn_see_more.setOnClickListener { _ ->
                    val intent = Intent(view.context, ReportHistoryActivity::class.java)
                    intent.putExtra("ME", it)
                    view.context.startActivity(intent)
                }

                view.btn_notification.setOnClickListener { _ ->
                    val sharedPreferences = view.context.getSharedPreferences("PREFERENCES", Context.MODE_PRIVATE)
                    val editor = sharedPreferences.edit()
                    editor.putInt("NOTIFICATION_COUNT", 0)
                    editor.apply()
                    onInitNotification(view)

                    val intent = Intent(view.context, NotificationActivity::class.java)
                    intent.putExtra("ME", it)
                    view.context.startActivity(intent)
                }

                Handler(Looper.getMainLooper()).postDelayed({ onInitNotification(view) }, 100)
            } else {
                view.top_container.visibility = View.GONE
                view.top_shimmer_container.visibility = View.VISIBLE
                view.top_shimmer_container.startShimmer()
            }
        })

        viewModel.reportTypes.observe(activity!!, {
            if (it != null) {
                view.emergency_shimmer_container.stopShimmer()
                view.emergency_shimmer_container.visibility = View.GONE
                view.emergency_recycler_view.visibility = View.VISIBLE
                view.emergency_recycler_view.adapter = EmergencyAdapter(view.context,
                        it.filter { type -> type.emergency_status }, viewModel)
            } else {
                view.emergency_recycler_view.visibility = View.GONE
                view.emergency_shimmer_container.visibility = View.VISIBLE
                view.emergency_shimmer_container.startShimmer()
            }
        })

        viewModel.reportHistories.observe(activity!!, {
            if (it != null) {
                view.history_shimmer_container.stopShimmer()
                view.history_shimmer_container.visibility = View.GONE

                if (it.isNotEmpty()) {
                    view.history_empty_container.visibility = View.GONE
                    view.history_recycler_view.visibility = View.VISIBLE
                    view.history_recycler_view.adapter = HistoryAdapter(view.context, it, viewModel.me.value!!)
                } else {
                    view.history_recycler_view.visibility = View.GONE
                    view.history_empty_container.visibility = View.VISIBLE
                }
            } else {
                view.history_empty_container.visibility = View.GONE
                view.history_recycler_view.visibility = View.GONE
                view.history_shimmer_container.visibility = View.VISIBLE
                view.history_shimmer_container.startShimmer()
            }
        })

        view.swipe_refresh.setOnRefreshListener {
            Handler(Looper.getMainLooper()).postDelayed({
                view.swipe_refresh.isRefreshing = false
                viewModel.me.value = null
                viewModel.reportTypes.value = null
                viewModel.reportHistories.value = null
                onInitNotification(view)
            }, 300)
        }

        return view
    }

    override fun onActivityResult(requestCode: Int, resultCode: Int, data: Intent?) {
        if (resultCode == Activity.RESULT_OK) {
            if (requestCode == 1) {
                viewModel.reportHistories.value = null
            }
        }
    }
}