package com.sipanduberadat.user.fragments

import android.os.Bundle
import android.os.Handler
import android.os.Looper
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import com.sipanduberadat.user.R
import com.sipanduberadat.user.adapters.MainReportViewPagerAdapter
import com.sipanduberadat.user.viewModels.MainViewModel
import kotlinx.android.synthetic.main.layout_main_report.view.*

class MainReportFragment: Fragment() {
    override fun onCreateView(
            inflater: LayoutInflater,
            container: ViewGroup?,
            savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.layout_main_report, container, false)
        val viewModel = ViewModelProvider(activity!!).get(MainViewModel::class.java)

        viewModel.me.observe(activity!!, {
            if (it?.pecalang != null) {
                view.view_pager.adapter = MainReportViewPagerAdapter(childFragmentManager)
                view.tabs.setupWithViewPager(view.view_pager)

                view.swipe_refresh.setOnRefreshListener {
                    Handler(Looper.getMainLooper()).postDelayed({
                        view.swipe_refresh.isRefreshing = false
                        viewModel.reportTypes.value = null
                        viewModel.reports.value = null
                        viewModel.guestReports.value = null
                    }, 300)
                }
            }
        })

        return view
    }
}