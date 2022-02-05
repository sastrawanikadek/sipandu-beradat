package com.sipanduberadat.guest.fragments

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import androidx.recyclerview.widget.LinearLayoutManager
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.adapters.ReportHandlerAdapter
import com.sipanduberadat.guest.viewModels.ReportDetailViewModel
import kotlinx.android.synthetic.main.layout_report_handler.view.*

class ReportDetailPecalangFragment: Fragment() {
    override fun onCreateView(
            inflater: LayoutInflater,
            container: ViewGroup?,
            savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.layout_report_handler, container, false)
        val viewModel = ViewModelProvider(activity!!).get(ReportDetailViewModel::class.java)

        view.recycler_view.apply {
            layoutManager = LinearLayoutManager(view.context, LinearLayoutManager.VERTICAL,
                    false)
            adapter = ReportHandlerAdapter(view.context, listOf(), null)
        }

        viewModel.report.observe(activity!!, {
            if (it != null) {
                if (it.pecalang_reports.isNotEmpty()) {
                    view.empty_container.visibility = View.GONE
                    view.recycler_view.visibility = View.VISIBLE
                    view.recycler_view.adapter = ReportHandlerAdapter(view.context, it.pecalang_reports,
                            null)
                } else {
                    view.recycler_view.visibility = View.GONE
                    view.empty_container.visibility = View.VISIBLE
                }
            }
        })

        viewModel.guestReport.observe(activity!!, {
            if (it != null) {
                if (it.pecalang_reports.isNotEmpty()) {
                    view.empty_container.visibility = View.GONE
                    view.recycler_view.visibility = View.VISIBLE
                    view.recycler_view.adapter = ReportHandlerAdapter(view.context, it.pecalang_reports,
                            null)
                } else {
                    view.recycler_view.visibility = View.GONE
                    view.empty_container.visibility = View.VISIBLE
                }
            }
        })

        return view
    }
}