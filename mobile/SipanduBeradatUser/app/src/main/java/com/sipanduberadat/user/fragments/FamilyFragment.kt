package com.sipanduberadat.user.fragments

import android.os.Bundle
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import androidx.core.widget.addTextChangedListener
import androidx.fragment.app.Fragment
import androidx.lifecycle.ViewModelProvider
import androidx.recyclerview.widget.LinearLayoutManager
import com.sipanduberadat.user.R
import com.sipanduberadat.user.adapters.FamilyAdapter
import com.sipanduberadat.user.models.Kerabat
import com.sipanduberadat.user.viewModels.MainViewModel
import kotlinx.android.synthetic.main.layout_family.view.*

class FamilyFragment: Fragment() {
    private lateinit var viewModel: MainViewModel
    private val filteredFamilies: MutableList<Kerabat> = mutableListOf()

    override fun onCreateView(
            inflater: LayoutInflater,
            container: ViewGroup?,
            savedInstanceState: Bundle?
    ): View? {
        val view = inflater.inflate(R.layout.layout_family, container, false)
        viewModel = ViewModelProvider(activity!!).get(MainViewModel::class.java)

        view.recycler_view.apply {
            layoutManager = LinearLayoutManager(view.context, LinearLayoutManager.VERTICAL,
                    false)
        }

        viewModel.families.observe(activity!!, {
            if (it != null) {
                filteredFamilies.clear()
                filteredFamilies.addAll(it)

                view.shimmer_container.stopShimmer()
                view.shimmer_container.visibility = View.GONE

                if (filteredFamilies.size > 0) {
                    val totalText = "${filteredFamilies.size} kerabat"

                    view.family_empty_container.visibility = View.GONE
                    view.content_container.visibility = View.VISIBLE
                    view.recycler_view.adapter = FamilyAdapter(view.context, filteredFamilies,
                            viewModel.me.value!!)
                    view.total.text = totalText
                } else {
                    view.content_container.visibility = View.GONE
                    view.family_empty_container.visibility = View.VISIBLE
                }
            } else {
                view.family_empty_container.visibility = View.GONE
                view.content_container.visibility = View.GONE
                view.shimmer_container.visibility = View.VISIBLE
                view.shimmer_container.startShimmer()
                view.search_family_edit_text.setText("")
            }
        })

        view.search_family_edit_text.addTextChangedListener { text ->
            if (viewModel.families.value != null && view.recycler_view.adapter != null) {
                if (text != null && text.isNotBlank()) {
                    filteredFamilies.clear()
                    filteredFamilies.addAll(viewModel.families.value!!.filter {
                        it.masyarakat.name.contains("$text", true)
                    })

                    if (filteredFamilies.size > 0) {
                        view.family_empty_container.visibility = View.GONE
                        view.content_container.visibility = View.VISIBLE

                        val totalText = "${filteredFamilies.size} dari total ${viewModel.families.value!!.size} kerabat"
                        view.recycler_view.adapter!!.notifyDataSetChanged()
                        view.total.text = totalText
                    } else {
                        view.content_container.visibility = View.GONE
                        view.family_empty_container.visibility = View.VISIBLE
                    }
                } else {
                    view.family_empty_container.visibility = View.GONE
                    view.content_container.visibility = View.VISIBLE

                    filteredFamilies.clear()
                    filteredFamilies.addAll(viewModel.families.value!!)

                    val totalText = "${filteredFamilies.size} kerabat"
                    view.recycler_view.adapter!!.notifyDataSetChanged()
                    view.total.text = totalText
                }
            }
        }

        return view
    }
}