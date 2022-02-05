package com.sipanduberadat.guest.fragments

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import androidx.recyclerview.widget.LinearLayoutManager
import com.sipanduberadat.guest.R
import com.sipanduberadat.guest.adapters.RequestFamilyAdapter
import com.sipanduberadat.guest.models.KerabatTamu
import com.sipanduberadat.guest.viewModels.MainViewModel
import kotlinx.android.synthetic.main.layout_request_family.view.*
import kotlinx.android.synthetic.main.layout_request_family.view.recycler_view
import kotlinx.android.synthetic.main.layout_request_family.view.shimmer_container

class RequestFamilyFragment: Fragment() {
    private val requestFamilies: MutableList<KerabatTamu> = mutableListOf()

    override fun onCreateView(
            inflater: LayoutInflater,
            container: ViewGroup?,
            savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.layout_request_family, container, false)
        val viewModel = ViewModelProvider(activity!!).get(MainViewModel::class.java)

        view.recycler_view.apply {
            layoutManager = LinearLayoutManager(view.context, LinearLayoutManager.VERTICAL,
                    false)
            adapter = RequestFamilyAdapter(view, requestFamilies)
        }

        viewModel.requestFamilies.observe(activity!!, {
            if (it != null) {
                requestFamilies.clear()
                requestFamilies.addAll(it)

                view.shimmer_container.stopShimmer()
                view.shimmer_container.visibility = View.GONE

                if (requestFamilies.size > 0) {
                    view.empty_container.visibility = View.GONE
                    view.recycler_view.visibility = View.VISIBLE
                    view.recycler_view.adapter!!.notifyDataSetChanged()
                } else {
                    view.recycler_view.visibility = View.GONE
                    view.empty_container.visibility = View.VISIBLE
                }
            } else {
                view.recycler_view.visibility = View.GONE
                view.empty_container.visibility = View.GONE
                view.shimmer_container.visibility = View.VISIBLE
                view.shimmer_container.startShimmer()
            }
        })

        return view
    }
}