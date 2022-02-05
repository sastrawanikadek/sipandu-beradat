package com.sipanduberadat.petugas.fragments

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import androidx.recyclerview.widget.LinearLayoutManager
import com.sipanduberadat.petugas.R
import com.sipanduberadat.petugas.adapters.ReportHandlerAdapter
import com.sipanduberadat.petugas.viewModels.ReportDetailViewModel
import kotlinx.android.synthetic.main.layout_report_handler.view.*

class ReportDetailPetugasFragment: Fragment() {
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
            adapter = ReportHandlerAdapter(view.context, null, listOf())
        }

        viewModel.report.observe(activity!!, {
            if (it != null) {
                if (it.petugas_reports.isNotEmpty()) {
                    view.empty_container.visibility = View.GONE
                    view.recycler_view.visibility = View.VISIBLE
                    view.recycler_view.adapter = ReportHandlerAdapter(view.context,
                            null, it.petugas_reports)
                } else {
                    view.recycler_view.visibility = View.GONE
                    view.empty_container.visibility = View.VISIBLE
                }
            }
        })

        viewModel.guestReport.observe(activity!!, {
            if (it != null) {
                if (it.petugas_reports.isNotEmpty()) {
                    view.empty_container.visibility = View.GONE
                    view.recycler_view.visibility = View.VISIBLE
                    view.recycler_view.adapter = ReportHandlerAdapter(view.context,
                            null, it.petugas_reports)
                } else {
                    view.recycler_view.visibility = View.GONE
                    view.empty_container.visibility = View.VISIBLE
                }
            }
        })

        return view
    }
}